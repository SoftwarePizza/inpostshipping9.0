{**
 * Copyright since 2021 InPost S.A.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the EUPL-1.2 or later.
 * You may not use this work except in compliance with the Licence.
 *
 * You may obtain a copy of the Licence at:
 * https://joinup.ec.europa.eu/software/page/eupl
 * It is also bundled with this package in the file LICENSE.txt
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the Licence is distributed on an AS IS basis,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the Licence for the specific language governing permissions
 * and limitations under the Licence.
 *
 * @author    InPost S.A.
 * @copyright Since 2021 InPost S.A.
 * @license   https://joinup.ec.europa.eu/software/page/eupl
 *}
<div class="js-inpost-is-non-standard-wrapper">
  <div class="row">
    <div class="col">
      <div class="form-group">
        <label class="form-control-label">
          {l s='Is non standard' mod='inpostshipping'}
        </label>
        <div class="input-group">
          <span class="ps-switch">
            <input type="radio"
                   name="parcels[{$index}][is_non_standard]"
                   id="is_non_standard_{$index}_off"
                   value="0"
                   {if !$isNonStandard}checked="checked"{/if}
            >
            <label for="is_non_standard_{$index}_off">{l s='No' mod='inpostshipping'}</label>
            <input type="radio"
                   name="parcels[{$index}][is_non_standard]"
                   id="is_non_standard_{$index}_on"
                   value="1"
                   {if $isNonStandard}checked="checked"{/if}
            >
            <label for="is_non_standard_{$index}_on">{l s='Yes' mod='inpostshipping'}</label>
            <a class="slide-button btn"></a>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="js-inpost-package-dimensions"{if $useTemplate} style="display: none"{/if}>
  <div class="row">
    <div class="col">
      <div class="form-group">
        <label for="length_{$index}" class="form-control-label required">
          {l s='Length' mod='inpostshipping'}
        </label>
        <div class="input-group">
          <div class="input-group-prepend">
            <div class="input-group-text">mm</div>
          </div>
          <input type="text" name="parcels[{$index}][dimensions][length]" id="length_{$index}" value="{$length|floatval}" class="form-control">
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group">
        <label for="width_{$index}" class="form-control-label required">
          {l s='Width' mod='inpostshipping'}
        </label>
        <div class="input-group">
          <div class="input-group-prepend">
            <div class="input-group-text">mm</div>
          </div>
          <input type="text" name="parcels[{$index}][dimensions][width]" id="width_{$index}" value="{$width|floatval}" class="form-control">
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group">
        <label for="height_{$index}" class="form-control-label required">
          {l s='Height' mod='inpostshipping'}
        </label>
        <div class="input-group">
          <div class="input-group-prepend">
            <div class="input-group-text">mm</div>
          </div>
          <input type="text" name="parcels[{$index}][dimensions][height]" id="height_{$index}" value="{$height|floatval}" class="form-control">
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group">
        <label for="weight_{$index}" class="form-control-label required">
          {l s='Weight' mod='inpostshipping'}
        </label>
        <div class="input-group">
          <div class="input-group-prepend">
            <div class="input-group-text">kg</div>
          </div>
          <input type="text" name="parcels[{$index}][weight]" id="weight_{$index}" value="{$weight|floatval}" class="form-control">
        </div>
      </div>
    </div>
  </div>
</div>
